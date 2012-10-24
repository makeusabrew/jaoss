{capture name="body"}
    <p>Smarty could not update the compiled template for {$e->ga("template")}.tpl. This usually means
    that the directory itself is writable, but the compiled version of this template has already
    been created by another user.</p>

    <p>The easiest thing to do is to clear out <b>tmp/templates_c</b>. Recent applications should be
    able to accomplish this by running <code>phing cleanup</code> from the command line.</p>
{/capture}
{include file="base.tpl"}
