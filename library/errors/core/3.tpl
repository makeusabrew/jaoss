{capture name="body"}
	<p>The template {$e->ga("tpl")} could not be found in any of the following paths:</p>
	<ol>
		{foreach from=$e->getArg("paths") item="path"}
			<li>{$path}</li>
		{/foreach}
	</ol>
{/capture}
{include file="base.tpl"}
