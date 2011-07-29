<?php

class Image {

    /**
     * every public method should eventually defer to this method
     */
    protected static function doResize($oldPath, $path, $x, $y, $width, $height, $scaleWidth, $scaleHeight) {

        list($oldWidth, $oldHeight, $mime) = self::getFileInfo($oldPath);

        Log::debug('resizing image of type ['.$mime.'] and dimensions ['.$oldWidth.'x'.$oldHeight.'] to ['.$width.'x'.$height.']');

        $oldHandle = null;
        $newHandle = imagecreatetruecolor($width, $height);

        switch ($mime) {
            case 'png':

                $oldHandle = imagecreatefrompng($oldPath);

                // we don't want to alpha blend on the *new* image - just a literal copy
                imagealphablending($newHandle, false);
                // but we do want to save the alpha value
                imagesavealpha($newHandle, true);

                // set black as the transparent colour for the new image
                imagecolortransparent($newHandle, imagecolorallocate($newHandle, 0, 0, 0));

                imagecopyresampled($newHandle, $oldHandle, $x, $y, 0, 0, $scaleWidth, $scaleHeight, $oldWidth, $oldHeight);

                $result = imagepng($newHandle, $path);


                break;

            case 'image/jpeg':
            case 'image/gif':
                $createMethod = self::getCreateMethodForMime($mime);
                $writeMethod  = self::getWriteMethodForMime($mime);

                $oldHandle = $createMethod($oldPath);

                imagecopyresampled($newHandle, $oldHandle, $x, $y, 0, 0, $scaleWidth, $scaleHeight, $oldWidth, $oldHeight);

                // only imagecreatefromjpeg takes a third param, but the others safely ignore it so it's all good
                $result = $writeMethod($newHandle, $path, Settings::getValue("images", "quality", 75));

                break;

            default:
                Log::warn('Unknown image mime type! ['.$mime.']');
                return false;
                break;
        }

        imagedestroy($oldHandle);
        imagedestroy($newHandle);

        if ($result === false) {
            Log::warn('Could not create new image ['.$newPath.'] from old image ['.$oldPath.'] with dimensions ['.$width.'x'.$height.']');
        }
        return $result;
    }

    protected static function getCreateMethodForMime($mime) {
        switch ($mime) {
            case 'image/jpeg':
                return 'imagecreatefromjpeg';
            case 'image/gif':
                return 'imagecreatefromgif';
        }
    }

    protected static function getWriteMethodForMime($mime) {
        switch ($mime) {
            case 'image/jpeg':
                return 'imagejpeg';
            case 'image/gif':
                return 'imagegif';
        }
    }

    protected static function getFileInfo($path) {
        $fileInfo = getimagesize($path);
        return array($fileInfo[0], $fileInfo[1], $fileInfo['mime']);
    }

    public static function resizeOnly($oldPath, $path, $width, $height) {
        return self::doResize($oldPath, $path, 0, 0, $width, $height, $width, $height);
    }

    public static function resizeCrop($oldPath, $path, $width, $height) {
        list($oldWidth, $oldHeight, $mime) = self::getFileInfo($oldPath);

        $oldRatio = $oldHeight / $oldWidth;
        $newRatio = $height / $width;

        $scaleWidth = 0;
        $scaleHeight = 0;

        if ($oldHeight == $oldWidth) {
            if ($height > $width) {
                $scaleWidth = $scaleHeight = $height;
            } else {
                $scaleWidth = $scaleHeight = $width;
            }
        } else {
            if ($oldRatio < $newRatio) {
                $scaleHeight = $height;
                $scaleWidth = round($scaleHeight * (1/$oldRatio));
            } else {
                $scaleWidth = $width;
                $scaleHeight = round($scaleWidth * $oldRatio);
            }
        }

        $x = ($scaleWidth - $width) / -2;
        $y = ($scaleHeight - $height) / -2;

        Log::debug('cropping to ['.$x.'x'.$y.'] and dimensions ['.$scaleWidth.'x'.$scaleHeight.']');

        return self::doResize($oldPath, $path, $x, $y, $width, $height, $scaleWidth, $scaleHeight);
    }
}
