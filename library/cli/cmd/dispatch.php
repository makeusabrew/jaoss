<?php

class Cli_Dispatch extends Cli {
    public function run() {
        $this->url();
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

        $path = $response->getPath();
        $matches = $path->getMatches();

        $this->setOutputColour(Colours::BLUE);
        $this->writeLine("Path Information");
        $this->writeLine("----------------");
        $this->writeLine("Pattern   : ".$path->getPattern());
        $this->writeLine("App       : ".$path->getApp());
        $this->writeLine("Controller: ".$path->getController());
        $this->writeLine("Action    : ".$path->getAction());
        $this->writeLine("Cacheable : ".($path->isCacheable() ? "Yes" : "No"));
        if ($path->isCacheable()) {
            $this->writeLine("Cache TTL : ".$path->getCacheTtl());
        }

        if (count($matches)) {
            $this->write("\n");
            $this->writeLine("Matches");
            $this->writeLine("-------");
            foreach ($matches as $match => $value) {
                $this->writeLine($match." => ".$value);
            }
        }
                
        $this->clearOutputColour();

        $this->write("\n");

        $this->setOutputColour(Colours::YELLOW);
        $this->writeLine("Response Headers");
        $this->writeLine("----------------");
        $this->writeLine("Response Code: ".$response->getResponseCode());
        if ($response->isRedirect()) {
            $this->writeLine("Redirect URL: ".$response->getRedirectUrl());
        }
        foreach ($response->getHeaders() as $key => $value) {
            $this->writeLine($key.": ".$value);
        }

        $this->clearOutputColour();

        $this->write("\n");

        // render body too?
        if (!$this->hasArg("--no-render")) {
            $this->write("\n");
            $this->write($response->getBody());
        }
    }
}
