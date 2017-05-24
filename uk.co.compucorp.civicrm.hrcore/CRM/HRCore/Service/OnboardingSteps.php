<?php

interface CRM_HRCore_Service_OnboardingSteps {
  const ACCOUNT_CREATED = 'User_account_created';
  const INVITATION_SENT = 'Invitation_email_sent';
  const PASSWORD_CREATED = 'Password_created';
  const DETAILS_COMPLETED = 'Personal_details_completed';
  const ADDRESS_COMPLETED = 'Address_completed';
  const CONTACT_INFO_COMPLETED = 'Contact_information_completed';
  const PAYROLL_COMPLETED = 'Payroll_details_completed';
  const EMERGENCY_CONTACT_ADDED = 'Emergency_contact_added';
  const ONBOARDED = 'Onboarded';

}
