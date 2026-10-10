sealed class SubscriptionRenewalState {
  const SubscriptionRenewalState();
}

final class SubscriptionRenewalInitial extends SubscriptionRenewalState {
  const SubscriptionRenewalInitial();
}

final class SubscriptionRenewalLoading extends SubscriptionRenewalState {
  final List<Map<String, dynamic>> items;
  const SubscriptionRenewalLoading([this.items = const []]);
}

final class SubscriptionRenewalLoaded extends SubscriptionRenewalState {
  final List<Map<String, dynamic>> items;
  final bool hasMore;
  final bool refreshing;
  const SubscriptionRenewalLoaded(this.items, {this.hasMore = false, this.refreshing = false});
}

final class SubscriptionRenewalSaving extends SubscriptionRenewalState {
  const SubscriptionRenewalSaving();
}

final class SubscriptionRenewalSaved extends SubscriptionRenewalState {
  final Map<String, dynamic> item;
  const SubscriptionRenewalSaved(this.item);
}

final class SubscriptionRenewalDeleting extends SubscriptionRenewalState {
  final int id;
  const SubscriptionRenewalDeleting(this.id);
}

final class SubscriptionRenewalFailure extends SubscriptionRenewalState {
  final String message;
  final List<Map<String, dynamic>> items;
  const SubscriptionRenewalFailure(this.message, [this.items = const []]);
}