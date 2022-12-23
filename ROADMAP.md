# SrLifeCycleManager Roadmap

This document holds ideas and improvements that could be implemented for this plugin in the future.
(If one of the following points has been implemented mark it checked)

- [ ] Implement some sort of "block service" (name pending), that is responsible to manage named-placeholders inside of
  language variables. A use case in this plugin can be found
  in [`ilSrNotificationSender`](classes/Notification/class.ilSrNotificationSender.php).

- [ ] Use PHP's `json_encode` and `json_decode` in
  the [`ilSrConfigRepository`](classes/Config/class.ilSrConfigRepository.php) for better encoded values. It could be
  even more improved by some sort of type-safe abstraction for configurations.

- [ ] The same dependencies are used in several instances and instantiated separately, which leads to multiple
  code-adjustments if the constructor signature is changed. This could be avoided by a local DIC or initialization.

- [ ] Add a post-composer-dump script that automatically reads `IEventListeners` into an artifact, which can then be
  required in the `IObserver` implementation rather than manually registering them. For this the setup's interface
  collector can be used.

- [ ] Alter the implementation of the token-system, so `IWhitelistRepository::redeem()` doesn't delete a token 
  immediately but marks it as redeemed instead (with an additional column). This way, users can be shown a proper
  error message which clearly states that the token they used has already been redeemed.
