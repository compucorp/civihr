<?php

class CRM_HRCore_Service_OnboardingStatusService {
  /**
   * @var int
   */
  protected $onboardingFieldId;

  /**
   * @var array
   */
  protected $onboardingStatuses = [];

  /**
   * @param int $contactId
   * @param string $step
   * @param bool $isCompleted
   */
  public function setStep($contactId, $step, $isCompleted) {
    $steps = $this->getOnboardingStepsForContact($contactId);
    $stepValue = $this->getOnboardingOptionValue($step);

    if ($isCompleted) {
      // append to array of completed steps
      if (!in_array($stepValue, $steps)) {
        $steps[] = $stepValue;
      }
    }
    else {
      // remove from complete steps
      $key = array_search($stepValue, $steps);
      if (NULL !== $key) {
        unset($steps[$key]);
      }
    }

    $this->replaceOnboardingStepsForContact($contactId, $steps);
  }

  /**
   * @param int $contactId
   * @param string $step
   *
   * @return bool
   */
  public function isCompleted($contactId, $step) {
    $steps = $this->getOnboardingStepsForContact($contactId);
    $stepValue = $this->getOnboardingOptionValue($step);

    return in_array($stepValue, $steps);
  }

  /**
   * @param int $contactId
   *
   * @return array
   */
  protected function getOnboardingStepsForContact($contactId) {
    $value = civicrm_api3('Contact', 'getvalue', [
      'return' => $this->getStatusCustomFieldName(),
      'id' => $contactId,
    ]);

    return $value ? $value : [];
  }

  /**
   * Replace current completed steps with a new set
   *
   * @param int $contactId
   * @param array $steps
   */
  protected function replaceOnboardingStepsForContact($contactId, $steps) {
    civicrm_api3('Contact', 'create', [
      'id' => $contactId,
      $this->getStatusCustomFieldName() => $steps,
    ]);
  }

  /**
   * @return int
   */
  protected function getStatusCustomFieldName() {
    if (!$this->onboardingFieldId) {
      $this->onboardingFieldId = civicrm_api3('CustomField', 'getvalue', [
        'return' => "id",
        'name' => "Onboarding_Status",
      ]);
    }

    return sprintf('custom_%s', $this->onboardingFieldId);
  }

  /**
   * Gets the value for an onboarding status option value
   *
   * @param string $name
   *   The machine name of the option value
   *
   * @return int|null
   *   The option value value value or null if not found
   */
  protected function getOnboardingOptionValue($name) {
    if (!$this->onboardingStatuses) {
      $params = ['option_group_id' => "onboarding_status"];
      $result = civicrm_api3('OptionValue', 'get', $params);
      $this->onboardingStatuses = $result['values'];
    }

    foreach ($this->onboardingStatuses as $status) {
      if ($status['name'] === $name) {
        return (int) $status['value'];
      }
    }

    throw new \Exception(
      sprintf('You have requested an unknown onboarding step, "%s"', $name)
    );
  }

}
