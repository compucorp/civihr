{if count( $wizard.steps ) > 1}
  <div class="panel panel-default crm_wizard__title">
    <div class="panel-body">
      <ul class="nav nav-pills">
        {section name=step loop=$wizard.steps}
        <li class="{if $wizard.currentStepNumber == $wizard.steps[step].stepNumber}active{/if}">
          <a {if $wizard.steps[step].link}href="{$wizard.steps[step].link}"{/if}>
            <span class="crm_wizard__title__number">{$wizard.steps[step].stepNumber}</span>
            {$wizard.steps[step].title}
          </a>
        </li>
        {/section}
      </ul>
    </div>
  </div>
{/if}
