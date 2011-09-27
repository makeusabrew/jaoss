{capture name="body"}
    <p>None of the currently loaded paths match the options provided. The options were:</p>

    {assign var="options" value=$e->getArg("options")}
    <dl>
        <dt>App</dt>
            <dd>{$options.app}</dd>
        <dt>Controller</dt>
            <dd>{$options.controller}</dd>
        <dt>Action</dt>
            <dd>{$options.action}</dd>
    </dl>

    <p>The paths loaded were:</p>
    <ul>
        {foreach from=$e->getArg("paths") item="path"}
            <li>{$path->getApp()|htmlentities}, {$path->getController()}, {$path->getAction()}</li>
        {/foreach}
    </ul>
{/capture}
{include file="base.tpl"}
