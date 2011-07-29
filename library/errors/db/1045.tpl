{capture name="body"}
    <p>This error usually means one of three things:</p>
    <ol>
        <li>Your database username is wrong</li>
        <li>Your password is wrong</li>
        <li>Your user doesn't have the privileges to carry out this operation</li>
    </ol>
    <p>If everything looks in order, double check the user has the correct privileges for the host you're trying to connect on.</p>
    <p>This error template could do with a bit of improvement - it can be found in <code>jaoss/library/errors/db/1045.tpl</code>.</p>
{/capture}
{include file="base.tpl"}
