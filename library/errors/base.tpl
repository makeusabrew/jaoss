<html>
	<head>
		<title>Error: {$e->getMessage()}</title>
		<style>
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
				{if $smarty.capture.body}
					{$smarty.capture.body}
				{else}
					<h1>{$e->getMessage()}</h1>
				
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
									<td>{$trace.file}</td>
									<td>{$trace.line}</td>
									<td>{$trace.function}</td>
							{/foreach}
						</tbody>
					</table>
				{/if}
			</div>
		</div>
	</body>
</html>
