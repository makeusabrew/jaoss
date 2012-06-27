{capture name="body"}
    No paths have been loaded so no URL can be matched. Try checking your application
    <code>paths.php</code> files are present &amp; correct. The applications loaded
    are:
    {assign var="apps" value=$e->getArg("apps")}
    <ul>
        {foreach from=$apps item="app"}
            <li>{$app->getTitle()}</li>
        {/foreach}
    </ul>
{/capture}
{include file="base.tpl"}
