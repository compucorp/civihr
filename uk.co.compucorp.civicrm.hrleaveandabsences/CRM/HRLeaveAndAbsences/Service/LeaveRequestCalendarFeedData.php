<?php

use CRM_HRLeaveAndAbsences_BAO_LeaveRequestCalendarFeedConfig as LeaveRequestCalendarFeedConfig;
use CRM_HRLeaveAndAbsences_BAO_LeaveRequest as LeaveRequest;
use CRM_HRLeaveAndAbsences_BAO_AbsenceType as AbsenceType;
use CRM_HRLeaveAndAbsences_Helper_CalendarFeed_LeaveTime as CalendarLeaveTimeHelper;

/**
 * Class CRM_HRLeaveAndAbsences_Service_LeaveRequestCalendarFeedData
 */
class CRM_HRLeaveAndAbsences_Service_LeaveRequestCalendarFeedData {

  /**
   * @var array
   */
  private $feedContacts;

  /**
   * @var array|null
   */
  private $leaveDayTypes;

  /**
   * @var string
   */
  private $feedHash;

  /**
   * @var \DateTime
   */
  private $startDate;

  /**
   * @var \DateTime
   */
  private $endDate;

  /**
   * @var LeaveRequestCalendarFeedConfig
   */
  private $feedConfig;

  /**
   * @var array
   */
  private $enabledLeaveTypesForFeed;

  /**
   * CRM_HRLeaveAndAbsences_Service_LeaveRequestCalendarFeedData constructor.
   *
   * @param string $feedHash
   */
  public function __construct($feedHash) {
    $this->feedHash = $feedHash;
    $this->setFeedConfig($feedHash);
    $this->setDataDateRange();
    $this->leaveDayTypes = array_flip(LeaveRequest::buildOptions('from_date_type', 'validate'));
    $this->enabledLeaveTypesForFeed = $this->getEnabledFeedLeaveTypes();
  }

  /**
   * Returns the prepared leave feed data for the feed configuration in
   * a format expected by the LeaveRequestCalendarFeedICal class.
   *
   * @return array
   */
  public function get() {
    $leaveRequests = $this->getLeaveRequests();
    $leaveRequestContacts = array_column($leaveRequests, 'contact_id');
    $contactNames = $this->getContactNames($leaveRequestContacts);
    $absenceTypesList = $this->enabledLeaveTypesForFeed;

    $leaveData = [];
    foreach($leaveRequests as $leaveRequest) {
      $leaveTypeName =
        !empty($absenceTypesList[$leaveRequest['type_id']]) ? $absenceTypesList[$leaveRequest['type_id']] : 'Leave';
      CalendarLeaveTimeHelper::adjust($leaveRequest);
      $leaveData[] = [
        'id' => $leaveRequest['id'],
        'contact_id' => $leaveRequest['contact_id'],
        'display_name' => $contactNames[$leaveRequest['contact_id']] . ' (' . $leaveTypeName . ')',
        'from_date' => $leaveRequest['from_date'],
        'to_date' => $leaveRequest['to_date'],
      ];
    }

    return $leaveData;
  }

  /**
   * Returns the Feed Config object.
   *
   * @return LeaveRequestCalendarFeedConfig
   */
  public function getFeedConfig() {
    return $this->feedConfig;
  }

  /**
   * Returns the start date for the date range
   * for which the feed data is retrieved
   *
   * @return \DateTime
   */
  public function getStartDate() {
    return $this->startDate;
  }

  /**
   * Returns the end date for the date range
   * for which the feed data is retrieved
   *
   * @return \DateTime
   */
  public function getEndDate() {
    return $this->endDate;
  }

  /**
   * Sets the date range for which the feed data is to be
   * retrieved. The default is today to 3 months time.
   */
  private function setDataDateRange() {
    $today = new DateTime();
    $threeMonths = new DateTime('+3 months');
    $today->setTime('00', '00');
    $threeMonths->setTime('23', '59');
    $this->startDate = $today;
    $this->endDate = $threeMonths;
  }

  /**
   * Returns the Leave Request for the feed configuration.
   *
   * @return array
   */
  private function getLeaveRequests() {
    $leaveTypes = array_keys($this->enabledLeaveTypesForFeed);
    $params = [
      'type_id' => ['IN' => $leaveTypes],
      'status_id' => ['IN' => ['approved', 'admin_approved']],
      'from_date' => ['>=' => $this->startDate->format('Y-m-d H:i:s')],
      'to_date' => ['<=' => $this->endDate->format('Y-m-d H:i:s')],
      'request_type' => array('!=' => LeaveRequest::REQUEST_TYPE_TOIL),
    ];

    if (!$this->isFeedForAllContacts()) {
      $params['contact_id'] = ['IN' => $this->getFeedContacts()];
    }

    $result = civicrm_api3('LeaveRequest', 'get', $params);

    return $result['values'];
  }

  /**
   * Sets the Feed Configuration object gotten from the given feed hash.
   *
   * @param string $hash
   */
  private function setFeedConfig($hash) {
    if (!$hash) {
      throw new RuntimeException('The feed hash should not be empty');
    }

    $feedConfig = new LeaveRequestCalendarFeedConfig();
    $feedConfig->is_active = 1;
    $feedConfig->hash = $hash;
    $feedConfig->find(true);

    if (!$feedConfig->id) {
      throw new RuntimeException('An enabled feed with the given hash does not exist!');
    }

    $this->feedConfig = $feedConfig;
  }

  /**
   * Returns the contacts whose leave data are to be returned for the
   * feed configuration.
   *
   * @return array
   */
  private function getFeedContacts() {
    if (!$this->feedContacts) {
      $composedOf = unserialize($this->feedConfig->composed_of);
      $departments = !empty($composedOf['department']) ? $composedOf['department'] : '';
      $locations = !empty($composedOf['location']) ? $composedOf['location'] : '';

      $params = ['return' => ['contact_id']];
      if ($departments) {
        $params['department'] = ['IN' => $departments];
      }

      if ($locations) {
        $params['location'] = ['IN' => $locations];
      }

      if ($locations && $departments) {
        $params['options'] = ['or' => [['department', 'location']]];
      }

      $result = civicrm_api3('ContactHrJobRoles', 'get', $params);

      $this->feedContacts = array_unique(array_column($result['values'], 'contact_id'));
    }

    return $this->feedContacts;
  }

  /**
   * Returns whether the feed is to be composed of all contacts or not.
   *
   * @return bool
   */
  private function isFeedForAllContacts() {
    $composedOf = unserialize($this->feedConfig->composed_of);

    if (empty($composedOf['location']) && empty($composedOf['department'])) {
      return TRUE;
    }

    return FALSE;
  }

  /**
   * Returns the leave types set for the feed configuration.
   *
   * @return array
   */
  private function getFeedLeaveTypes() {
    $composedOf = unserialize($this->feedConfig->composed_of);

    return $composedOf['leave_type'];
  }

  /**
   * Returns the enabled leave types for the feed config from the list of
   * leave types set for the feed configuration. The feed data is not supposed to
   * contain data for disabled leave types.
   *
   * @return array
   */
  private function getEnabledFeedLeaveTypes() {
    $feedLeaveTypes = $this->getFeedLeaveTypes();
    $absenceTypes = AbsenceType::getEnabledAbsenceTypes();
    $absenceTypesList = [];

    foreach ($absenceTypes as $absenceType) {
      if (in_array($absenceType->id, $feedLeaveTypes)) {
        $absenceTypesList[$absenceType->id] = $absenceType->title;
      }
    }

    return $absenceTypesList;
  }

  /**
   * Return an array of contact display names indexed by contact IDs
   *
   * @param array $contactId
   *
   * @return array
   */
  private function getContactNames($contactId) {
    if (empty($contactId)) {
      return [];
    }
    $result = civicrm_api3('Contact', 'get', [
      'contact_id' => ['IN' => $contactId],
      'return' => ['display_name']
    ]);

    return array_column($result['values'], 'display_name', 'id');
  }
}
