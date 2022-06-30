# SrLifeCycleManager Changelog

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
