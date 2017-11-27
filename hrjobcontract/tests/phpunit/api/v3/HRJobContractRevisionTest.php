<?php

use CRM_HRCore_Test_Fabricator_Contact as ContactFabricator;
use CRM_Hrjobcontract_Test_Fabricator_HRJobContract as HRJobContractFabricator;

/**
 * Class api_v3_HRJobContractRevisionTest
 *
 * @group headless
 */
class api_v3_HRJobContractRevisionTest extends CRM_Hrjobcontract_Test_BaseHeadlessTest {

  public function testItAcceptsBothIntegerAndStringForChangeReasonValue() {
    $contact = ContactFabricator::fabricate();
    $contract = HRJobContractFabricator::fabricate(['contact_id' => $contact['id']], ['period_start_date' => '2015-01-01']);
    $reason = $this->getReasonChangeOption();

    $revision = $this->createRevision($contract['id'], $reason);
    $this->assertEquals($revision['is_error'], 0);

    $reason = $this->updateReasonChangeOptionValue($reason['id'], 'Lorem Ipsum');

    $revision = $this->createRevision($contract['id'], $reason);
    $this->assertEquals($revision['is_error'], 0);
  }

  /**
   * Creates a revision via the api
   *
   * @param  [int] $contract_id
   * @param  [Array] $reason    The reason change option
   * @return [Array]
   */
  private function createRevision($contract_id, $reason) {
    return civicrm_api3('HRJobContractRevision', 'create', array(
      'jobcontract_id' => $contract_id,
      'change_reason' => $reason['value'],
      'effective_date' => '2016-11-04',
    ));
  }

  /**
   * Returns a reason change option
   *
   * @return [Array]
   */
  private function getReasonChangeOption() {
    return civicrm_api3('OptionValue', 'get', array(
      'sequential' => 1,
      'option_group_id' => "hrjc_revision_change_reason",
    ))['values'][0];
  }

  /**
   * Updates the value of the reason change option with the given id
   *
   * @param  [int] $id
   * @param  [string] $value
   * @return [Array]
   */
  private function updateReasonChangeOptionValue($id, $value) {
    return civicrm_api3('OptionValue', 'create', array(
      'sequential' => 1,
      'id' => $id,
      'value' => $value,
    ))['values'][0];
  }
}
