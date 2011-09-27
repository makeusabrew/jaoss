{capture name="body"}
    <p>The model <strong>{$e->getArg("model")}</strong> could not be found in any of the loaded
    application directories. The most common reason for this is that the file was not named
    correctly - it should be named <strong>{$e->getArg("file")}</strong>.</p>

    <p>If your file is named correctly, check the name of the class is correct. If it is, check
    that the application has been loaded and the file is located in the correct place. The
    application paths searched were:

    <ul>
        {assign var="apps" value=$e->getArg("apps")}
        {foreach from=$apps item="app"}
            <li>apps/{$app}/models/</li>
        {/foreach}
    </ul>
{/capture}
{include file="base.tpl"}
