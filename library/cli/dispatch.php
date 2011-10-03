<?php

class Cli_Dispatch extends Cli {
    public function run() {
        if (count($this->args) === 0) {
            $method = "url";
            $this->writeLine("Assuming url option - only one available!");
        } else {
            $method = $this->shiftArg();
        }
        $this->$method();
    }

    protected function url() {
        if (count($this->args) === 0) {
            // no problemo, go interactive
            $url = $this->prompt('Please enter a URL to dispatch', '/');
        } else {
            $url = $this->shiftArg();
        }

        // always init a test request for now
        $request = new TestRequest();
        $request->dispatch($url);

        $response = $request->getResponse();
        $this->writeLine(Colours::yellow("Response Headers"));
        $this->writeLine(Colours::yellow("Response Code: ".$response->getResponseCode()));
        if ($response->isRedirect()) {
            $this->writeLine(Colours::yellow("Redirect URL: ".$response->getRedirectUrl()));
        }
        foreach ($response->getHeaders() as $key => $value) {
            $this->writeLine(Colours::yellow($key.": ".$value));
        }

        // render body too?

        if (!$this->hasArg("--no-render")) {
            $this->write("\n");
            $this->write($response->getBody());
        }
    }
}
