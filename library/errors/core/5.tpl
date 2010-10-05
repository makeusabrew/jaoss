{capture name="body"}
    <p>The logfile {$e->ga("path")} either does not exist or is not writable. Please modify the permissions
    on it so your web user can write to it.</p>
{/capture}
{include file="base.tpl"}
