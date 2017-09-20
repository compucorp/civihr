{if count( $wizard.steps ) > 1}
  <div class="panel panel-default crm_wizard__title">
    <div class="panel-body">
      <ul class="nav nav-pills">
        {section name=stepIndex loop=$wizard.steps}
        {assign var="step" value=$wizard.steps[stepIndex]}
        <li class="{if $wizard.currentStepNumber == $step.stepNumber}active{/if}">
          <a {if $step.link}href="{$step.link}"{/if}>
            <span class="crm_wizard__title__number">{$step.stepNumber}</span>
            {$step.title}
          </a>
        </li>
        {/section}
      </ul>
    </div>
  </div>
{/if}
