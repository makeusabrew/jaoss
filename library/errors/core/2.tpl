{capture name="body"}
	Action <strong>{$e->getArg("action")}</strong> does not exist in controller
	<strong>{$e->getArg("controller")}</strong> (in {$e->getArg("path")}).
    <h2>Things to check</h2>
    <ul>
        <li>Is {$e->getArg("controller")} the right controller class?</li>
        <li>Is {$e->getArg("action")} the right action?</li>
    </ul>
{/capture}
{include file="base.tpl"}
