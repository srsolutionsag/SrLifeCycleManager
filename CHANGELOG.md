# SrLifeCycleManager Changelog

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
- Introduced new event-system which can be used via plugin-instance [`ilSrLifeCycleManagerPlugin`](./classes/class.ilSrLifeCycleManagerPlugin.php).
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
