{capture name="body"}
    <p>An error occured whilst executing the smarty template. This is usually when something
    has gone wrong at run-time such as a smarty include file not being found - if you know
    of any other reasons please add them to this template in <code>jaoss/library/errors/smarty/exception.tpl</code>.</p>
    <div>
        <small>{$e->getMessage()}</small>
    </div>
{/capture}
{include file="base.tpl" title="Smarty Error: `$e->getMessage()`"}
