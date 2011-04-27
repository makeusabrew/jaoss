{capture name="body"}
    <p>{$e->getMessage()} in file <code>{$e->getFile()}</code> line <strong>{$e->getLine()}</strong></p>
{/capture}
{include file="base.tpl" title="PHP Error (`$e->getSeverity()`)"}
