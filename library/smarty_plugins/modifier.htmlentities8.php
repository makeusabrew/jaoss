<?php
function smarty_modifier_htmlentities8($string)
{ 
    return htmlentities($string, null, "UTF-8");
}
