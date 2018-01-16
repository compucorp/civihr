<?php

use CRM_HRCore_Service_Stats_StatsSender as StatsSender;
use CRM_Utils_HttpClient as HttpClient;
use CRM_HRCore_Model_CiviHRStatistics as CiviHRStatistics;
use CRM_HRCore_Test_BaseHeadlessTest as BaseHeadlessTest;
use CRM_HRCore_Service_Stats_StatsJSONConvertor as StatsJSONConvertor;

/**
 * @group headless
 */
class StatsSenderTest extends BaseHeadlessTest {

  public function testSuccessfulResponseWillNotThrowException() {
    $stats = new CiviHRStatistics();
    $json = StatsJSONConvertor::toJson($stats);

    $response = [HttpClient::STATUS_OK, ''];
    $client = $this->prophesize(HttpClient::class);
    $client->post(CIVIHR_STATISTICS_ENDPOINT, $json)->willReturn($response);
    
    $sender = new StatsSender($client->reveal());

    $sender->send($stats);
  }

  public function testNonOKStatusWillThrowException() {
    $stats = new CiviHRStatistics();
    $json = StatsJSONConvertor::toJson($stats);

    $msg = 'Failed sending CiviHR stats: <error message>';
    $this->setExpectedException(\Exception::class, $msg);

    $client = $this->prophesize(HttpClient::class);
    $response = [HttpClient::STATUS_DL_ERROR, '<error message>'];
    $client->post(CIVIHR_STATISTICS_ENDPOINT, $json)->willReturn($response);
    
    $sender = new StatsSender($client->reveal());

    $sender->send($stats);
  }
}
