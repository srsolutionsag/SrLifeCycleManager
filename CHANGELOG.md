# SrLifeCycleManager Changelog

## 1.7.3

- Fixed an issue where sent reminders weren't marked which lead to objects never being deleted.

## 1.7.2

- Removed postpone- and opt-out-action from `ilSrRoutineAssignmentTable` because the visibility of these actions got
  more complex and should only be made via repository tool.
- Fixed redirects in `ilSrRoutineGUI` and `ilSrRoutineAssignmentGUI` if an object was requested.
- Added an edit assignment action to the repository tool for assigned routines.

## 1.7.1

- Fixed several issues, where:
    - elongations and opt-outs via repository tool lead to a fatal error because the token was missing.
    - the elongation action was still available after an object has already been opted-out.
    - routines were displayed in both, affecting- and assigned-routine-lists.
    - storing a routine without an elongation cooldown lead to a database exception.
    - long titles of a routine lead to shifted action-dropdowns in the repository tool lists.
- Elongations and cooldowns are now required if optional form-group has been checked.
- Improved the calculation of gaps between datetime objects, so it now returns 1 if a new day has began.
- Adjusted action names (language variables) in both routine lists, that lead to an "overcrowded" action-dropdown.

## 1.7.0

- Added a token-system which is used when generating link targets for elongations or opt-outs. This prevents multiple
  object administrators from postponing the object with the same link more than once.
    - IMPORTANT: this also means, any previously sent reminders now contain invalid links for elongations and opt-outs.
- Added a `cooldown` property to routines that will be considered when making elongations (or postponements). The
  cooldown for all existing routines has been defaulted to 1, which means after an elongation has been made it cannot be
  postponed again for the amount of days the `cooldown` property holds.
- Added a new configuration to enforce mail-forwarding, even though users might have disabled it. This config will be
  considered in `ilSrNotificationSender` which will only forward mails if the user has disabled it.
- Improved the overall UI of the repository tool, which now lists routines as items within an item group.
    - The repository tool will now also display the owner, deletion-date and expiry-date (of whitelists) for affecting
      routines.
    - The repository tool does also contain a new action for un-doing previously made opt-outs.
- Several things have been refactored:
    - Routine cron-job will now send reminders for the correct amount of days before an object deletion if it has been
      whitelisted.
    - Whitelist elongations (or postponements) will now be appended to the relatively calculated deletion date instead
      of being added to the current date.
    - The whitelist table does now store an absolute expiry-date instead of the relative elongation.
    - A new `DateTimeHelper` trait is available that should be used for clean creation of `DateTime` objects. It's
      already integrated in several repositories, dynamic attributes and GUI classes.
    - PHPUnit tests due to the adjusted behaviour of the routine cron-job.

## 1.6.3

- Reminder repository can now handle stored mysql datetime values.
- Fixed lang-var reference in `ilSrWhitelistTable` (action_object_view).

## 1.6.2

- Fixed routine cron-job, so it deletes more than just one object per iteration.

## 1.6.1

- Fixed routine cron-jobs and unit-tests which contained wrong importations of reminder- and notifications-classes.

## 1.6.0

- Refactored notifications and split them up into `reminders` and `confirmations`, whereas
    - reminders are sent for the configured amount of days before an object deletion.
    - confirmations are sent whenever certain events occur (postponements, opt-outs, deletions).
- Fixed an issue where postponements and opt-outs were possible for the ILIAS root (ref-id 1).
- Changed wording from `days_before_submission` to `days_before_deletion` to be more accurate.
- Introduced new event-system which can be used via
  plugin-instance [`ilSrLifeCycleManagerPlugin`](./classes/class.ilSrLifeCycleManagerPlugin.php).
- Added event-listener which sends confirmations for certain actions.

## 1.5.0

- Administrators (`SYSTEM_ROLE_ID`) are now permitted to always opt out an object that is affected by a routine.
- The configuration GUI now also shows a "back-to" tab when not in administration context.
- Removed unused method `ilSrAbstarctGUI::renderMulti()`.

## 1.4.3

- Fixed an issue where query-parameters were not passed along to `ilSrRoutinePreviewGUI`.
- Added CaptainHook configuration to enforce pretty commit-messages.

## 1.4.2

- An empty string can now be submitted in the plugin-configuration's "notification email" field.
- Common attribute 'DateTime' can now be compared with lesser-equal or greater-equal correctly.
- Routine-assignments can now be edited properly (in both repository and administration context).
- Tool routine-lists now show the according actions again (postpone or opt-out, if supported).
