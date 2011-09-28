{capture name="body"}
	<p>The template {$e->ga("tpl")} could not be found in any of the following paths (searched in order):</p>
	<ol>
		{foreach from=$e->getArg("paths") item="path"}
			<li>{$path}</li>
		{/foreach}
	</ol>
    <h2>Things to check</h2>
    <ul>
        <li>Is {$e->getArg("tpl")} the correct template name? (Note: you never include the <code>.tpl</code> extension)</li>
        <li>Has the app which contains this template been loaded, and if so is it in the right place (under <code>views/</code>)?</li>
    </ul>
{/capture}
{include file="base.tpl"}
