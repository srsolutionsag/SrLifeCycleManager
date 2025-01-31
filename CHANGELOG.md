# SrLifeCycleManager Changelog

## 3.0.1

- Fixed an issue where UTF8 strings have not been cropped properly.

## 3.0.0

- Added ILIAS 9 compatibility.

## 2.1.1

- Fixed an issue where the plugin could not be installed/updated via setup (CLI) due to an undefined service.

## 2.1.0

- Added `[OBJECT_TITLE]` shortcode for notification subjects, which translates to the corresponding ILIAS object title
  at runtime.

## 2.0.7

- Fixed an issue where user object instances have been created unsafely which has led to cron job crashes sometimes.

## 2.0.6

- Fixed an ILIAS 8 compatibility issue which made the routine-assignment and whitelist table inaccessible.

## 2.0.5

- Fixed an issue where routines could not be assigned to objects anymore, due to an error in the form validation.
- Fixed an issue where cron job messages were too long (more than 400 characters).

## 2.0.4

- Routine assignments which are not recursive will provide direct children instead of the assigned object now.
- Routine list in ILIAS tool will no longer show a deletion date for opt-outs.

## 2.0.3

- Fixed an issue where database tables have not been deleted if the plugin was uninstalled.

## 2.0.2

- Added PHP 7.4 compatibility.

## 2.0.1

- Fixed an issue where the background-task for generating a preview was incompatible with ILIAS 8.

## 2.0.0

- Added ILIAS 8 compatibility.

## 1.9.0

- Added an ILIAS background-task to create a preview of the deletion-process, which can generates a text-file containing
  all objects which will be deleted. This file can then be downloaded by the user.

## 1.8.1

- Improved object-retrieval in routine cron-job, which now only traverses the repository tree downwards from the
  routines assigned ref-id.
- Introduced an `INotifier` which is responsible for pinging the cron-manager during cron-job runs. This prevents the
  cron-jobs from timing out.
- Properly implemented the [`observer pattern`](https://refactoring.guru/design-patterns/observer) and improved the
  naming to comply with PHPs subject-observer terminology.
- Fixed an issue where the repository tool produced unnecessary log-entries, because `ilParticipants` has been used on
  objects which don't support it.

## 1.8.0

- Implemented a `IRecipientRetriever` which provides the `INotificationSender` with the corresponding recipients
  of the object which should be notified.
- Migrated course and group attributes generic to an ILIAS object to new dynamic attributes. This allows to easily adopt
  new object-types in the future, since these dynamic attributes are available for ALL objects.
- Migrated course and group member attributes to new dynamic attributes. This allows to easily adopt new object-types
  which support memberships in the future.
- Fixed an issue where only container repository objects were delivered for deletion.
- Added info-byline for routine-elongation-cooldowns.
- Improved routine cron-job and it's unit tests.
- Added new dynamic attributes for surveys:
    - Survey participants (not the same as memberships)
    - Survey questions
    - Survey results
- Fixed an issue where the assignment-form would provide other ILIAS data as well when searching for objects.
- Fixed a typo in the delete-clause of a routine which lead to routines not being deleted.
- Applied PSR-12 to adjusted PHP files.

## 1.7.9

- Fixed an issue where whitelist requests lead to a database error, due to `date` still being `NOT NULL`.

## 1.7.8

- Fixed an issue where the `ISentReminder::isElapsed()` lead to a type-error instead of the proper logic exception.
- Fixed an issue where the routine cron job tried to check if an un-sent reminder has been elapsed yet.

## 1.7.7

- Fixed an issue where redirects with the wrong ref-id were made during the opt-out or postponement process.
- Fixed an issue where objects which have been opted-out from deletion could still be postponed via whitelist token.
- Fixed an issue where opt-out whitelist tokens were always invalid due to the wrong event-check.
- Improved whitelist entries to allow `NULL` as the date, because opt-outs should not affect postponements when undone.
- Improved language variable that informs the user about an invalid whitelist token.

## 1.7.6

- Fixed an issue where too long whitelist tokens were generated, which lead to tokens not being found eventhough they
  should have been valid.

## 1.7.5

- Fixed an issue where the routine cron-job deleted objects too soon because the amount of days before deletion wasn't
  taken into account.
- Fixed an issue where tokens which have been created before the renaming of events lead to invalid whitelist requests,
  due to them not being found anymore.

## 1.7.4

- Introduced a new configuration to enable/disable debug mode. If enabled, any exception thrown by a command class (GUI)
  of this plugin will be printed with it's stacktrace. This will help reproducing issues on a local machine.
- Fixed an issue where confirmations which have been created before the renaming of events lead to an error when
  editing.

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
