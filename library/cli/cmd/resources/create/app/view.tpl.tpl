{literal}{extends file='default/views/base.tpl'}
{block name='title'}{$smarty.block.parent} - {/literal}{$app|ucfirst}{literal}{/block}
{block name='body'}
    <h1>Welcome to your new application!</h1>
    <p>You've created a new application which can be found in <code>{/literal}{$fullPath}{literal}</code>.</p>

    <p>Your application has been created with a basic controller with one action (this one). It
    can be found at <code>{/literal}{$fullPath}{literal}/controllers/{/literal}{$controller|strtolower}{literal}.php</code>.</p>

    <p>You can edit this template at <code>{/literal}{$fullPath}{literal}/views/{/literal}{$action}{literal}.tpl</code>.</p>

    <p>You can add, edit and remove this path from the paths file located at <code>{/literal}{$fullPath}{literal}/paths.php</code>.</p>

    {/literal}{if isset($model)}{literal}<p>Your application also has a basic model which can
    be found at <code>{/literal}{$fullPath}{literal}/models/{/literal}{$model|strtolower}{literal}s.php</code>.</p>{/literal}{/if}{literal}
{/block}{/literal}
