{capture name="body"}
    <p>An error occured whilst processing the smarty template - the only reason known so far is because of a syntax error. The full exception
    message is shown below to try and help you out a bit:</p>
    <div>
        <small>{$e->getMessage()}</small>
    </div>
{/capture}
{include file="base.tpl" title="Smarty Syntax Error"}
