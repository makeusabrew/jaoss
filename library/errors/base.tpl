<html>
	<head>
		<title>Error: {if isset($title)}{$title}{else}{$e->getMessage()}{/if}</title>
		<style>
            html, body {
                font-size:11px;
                font-family:Helvetica;
            }

            tbody {
                font-family:courier;
                font-size:11px;
            }

			#error_container {
                border:1em solid #a00;
			}
			#error_inner {
				padding:1em;
				background:#fff;
			}
            h1 {
                margin:0;
                padding:0em 0.5em 0.5em 0.5em;
				background:#a00;
                color:#fff;
            }

            #backtrace {
                padding:0.25em 1em 0.5em 1em;
                background: #f8f8f8;
            }

            #meta {
                padding:0.25em 1em 0.5em 1em;
                background:#FFFBE3;
            }

            #backtrace h2 {
                border-bottom:1px solid #bbb;
            }
		</style>
	</head>
	<body>
		<div id="error_container">
            <h1>{if isset($title)}{$title}{else}{$e->getMessage()}{/if}</h1>
			<div id="error_inner">				
				{if $smarty.capture.body}
					{$smarty.capture.body}
				{/if}
            </div>
            <div id="backtrace">
                <h2>Backtrace</h2>
                <table>
                    <thead>
                        <tr>
                            <th>File</th>
                            <th>Line</th>
                            <th>Function</td>
                        </tr>
                    </thead>
                    <tbody>
                        {foreach $e->getTrace() as $trace}
                            <tr>
                                <td>{if isset($trace.file)}{$trace.file}{else}-{/if}</td>
                                <td>{if isset($trace.line)}{$trace.line}{else}-{/if}</td>
                                <td>{if isset($trace.class)}{$trace.class}{/if}{if isset($trace.type)}{$trace.type}{/if}{if isset($trace.function)}{$trace.function}{if isset($trace.args)}({foreach from=$trace.args item="_arg" name="aloop"}{if is_object($_arg)}Object{elseif is_array($_arg)}Array{else}{$_arg}{/if}{if !$smarty.foreach.aloop.last}, {/if}{/foreach}){/if}{else}-{/if}</td>
                            </tr>
                        {/foreach}
                    </tbody>
                </table>
            </div>
            {if isset($request)}
                <div id="meta">
                    <h2>Request Information</h3>
                    <div>
                        Response Code: <strong>{$response->getResponseCode()}</strong>
                    </div>
                    <div>
                        Time: <strong>{$request->getTimestamp()|date_format:"H:i:s"}</strong>
                    </div>
                    <div>
                        Method: <strong>{$request->getMethod()}</strong>
                    </div>
                    <div>
                        URL: <strong>{$request->getFullUrl()}</strong>
                    </div>
                    <div>
                        AJAX: <strong>{if $request->isAjax()}Yes{else}No{/if}</strong>
                    </div>
                    <div>
                        Request IP: <strong>{$request->getIp()}</strong>
                    </div>
                    <div>
                        User Agent: <strong>{$request->getUserAgent()}</strong>
                    </div>
                </div>
            {/if}
		</div>
	</body>
</html>
