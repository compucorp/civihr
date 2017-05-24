<?php

use CRM_HRCore_Service_OnboardingStatusService as OnboardingStatusService;
use CRM_HRCore_Service_OnboardingSteps as Steps;

/**
 * @group headless
 */
class OnboardingStatusServiceTest extends CRM_HRCore_Test_BaseHeadlessTest {

  public function testCheckingAndSetting() {
    $contact = CRM_HRCore_Test_Fabricator_Contact::fabricate();
    $contactId = $contact['id'];
    $service = new OnboardingStatusService();
    $step = Steps::ACCOUNT_CREATED;
    $completedDefault = $service->isCompleted($contactId, $step);
    $service->setStep($contactId, $step, TRUE);
    $completedNew = $service->isCompleted($contactId, $step);

    $this->assertFalse($completedDefault);
    $this->assertTrue($completedNew);
  }

  public function testUnsettingStep() {
    $contact = CRM_HRCore_Test_Fabricator_Contact::fabricate();
    $contactId = $contact['id'];
    $service = new OnboardingStatusService();
    $step = Steps::EMERGENCY_CONTACT_ADDED;
    $service->setStep($contactId, $step, TRUE);
    $this->assertTrue($service->isCompleted($contactId, $step));

    $service->setStep($contactId, $step, FALSE);
    $this->assertFalse($service->isCompleted($contactId, $step));
  }

  public function testMultipleSteps() {
    $contact = CRM_HRCore_Test_Fabricator_Contact::fabricate();
    $contactId = $contact['id'];
    $service = new OnboardingStatusService();
    $completed1 = Steps::EMERGENCY_CONTACT_ADDED;
    $completed2 = Steps::ADDRESS_COMPLETED;
    $completed3 = Steps::ONBOARDED;
    $incomplete1 = Steps::ACCOUNT_CREATED;
    $incomplete2 = Steps::PAYROLL_COMPLETED;

    $service->setStep($contactId, $completed1, TRUE);
    $service->setStep($contactId, $completed2, TRUE);
    $service->setStep($contactId, $completed3, TRUE);

    $this->assertTrue($service->isCompleted($contactId, $completed1));
    $this->assertTrue($service->isCompleted($contactId, $completed2));
    $this->assertTrue($service->isCompleted($contactId, $completed3));

    $this->assertFalse($service->isCompleted($contactId, $incomplete1));
    $this->assertFalse($service->isCompleted($contactId, $incomplete2));
  }

}
