<?php

class CRM_HRCore_HookListener_EventBased_OnSummary extends CRM_HRCore_HookListener_BaseListener {

  public function handle($contactId, &$content, &$contentPlacement) {
    $uf = _get_uf_match_contact($contactId);

    if (empty($uf) || empty($uf['uf_id'])) {
      return NULL;
    }

    $user = user_load($uf['uf_id']);
    $content['userid'] = $uf['uf_id'];
    $content['username'] = !empty($user->name) ? $user->name : '';
    $contentPlacement = NULL;
  }
}
