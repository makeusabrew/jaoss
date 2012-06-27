{capture name="body"}
    <p>No matching path could be found matching the name {$e->ga("name")}</p>

    <p>The following path names were loaded:</p>

    {assign var="paths" value=$e->getArg("paths")}
    <ul>
        {foreach $paths as $path}
            <li>{$path->getName()} &rarr; {$path->getPattern()}</li>
        {/foreach}
    </ul>
{/capture}
{include file="base.tpl"}
