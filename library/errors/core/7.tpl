{capture name="body"}
    <p>Controller class {$e->ga("class")} could not be found.</p>
    {if $e->getArg("apps")}
        <p>The following app paths were checked:</p>
        <ul>
            {foreach from=$e->getArg("apps") item="app"}
                <li>{$app}</li>
            {/foreach}
        </ul>
    {elseif $e->getArg("path")}
        <p>The path searched was {$e->ga("path")}.</p>
    {/if}
{/capture}
{include file="base.tpl"}
