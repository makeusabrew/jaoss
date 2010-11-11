{capture name="body"}
    <p>Controller class {$e->ga("class")} could not be found.</p>
    {if $e->getArg("apps")}
        <p>The following app paths were checked:</p>
        <ul>
            {foreach from=$e->getArg("apps") item="app"}
                <li>{$app}</li>
            {/foreach}
        </ul>
    {elseif $e->getArg("app_path")}
        <p>The app path {$e->ga("app_path")} was checked.</p>
    {/if}
{/capture}
{include file="base.tpl"}
