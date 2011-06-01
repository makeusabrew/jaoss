<html>
	<head>
		<title>Error: {if isset($title)}{$title}{else}{$e->getMessage()}{/if}</title>
		<style>
            html, body {
                font-size:11px;
                font-family:Arial;
            }

            tbody {
                font-family:courier;
                font-size:11px;
            }

			#error_container {
				padding:1em;
				background:#a00;
			}
			#error_inner {
				padding:1em;
				background:#fff;
			}
		</style>
	</head>
	<body>
		<div id="error_container">
			<div id="error_inner">				
				<h1>{if isset($title)}{$title}{else}{$e->getMessage()}{/if}</h1>
				{if $smarty.capture.body}
					{$smarty.capture.body}
				{/if}
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
								<td>{if isset($trace.class)}{$trace.class}{/if}{if isset($trace.type)}{$trace.type}{/if}{if isset($trace.function)}{$trace.function}{if isset($trace.args)}({foreach from=$trace.args item="_arg" name="aloop"}{if !is_object($_arg)}{$_arg}{else}Object{/if}{if !$smarty.foreach.aloop.last}, {/if}{/foreach}){/if}{else}-{/if}</td>
                            </tr>
						{/foreach}
					</tbody>
				</table>
			</div>
		</div>
	</body>
</html>
