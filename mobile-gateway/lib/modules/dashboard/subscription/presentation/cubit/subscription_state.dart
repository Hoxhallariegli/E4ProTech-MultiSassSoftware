sealed class SubscriptionState {
  const SubscriptionState();
}

final class SubscriptionInitial extends SubscriptionState {
  const SubscriptionInitial();
}

final class SubscriptionLoading extends SubscriptionState {
  final List<Map<String, dynamic>> items;
  const SubscriptionLoading([this.items = const []]);
}

final class SubscriptionLoaded extends SubscriptionState {
  final List<Map<String, dynamic>> items;
  final bool hasMore;
  final bool refreshing;
  const SubscriptionLoaded(this.items, {this.hasMore = false, this.refreshing = false});
}

final class SubscriptionSaving extends SubscriptionState {
  const SubscriptionSaving();
}

final class SubscriptionSaved extends SubscriptionState {
  final Map<String, dynamic> item;
  const SubscriptionSaved(this.item);
}

final class SubscriptionDeleting extends SubscriptionState {
  final int id;
  const SubscriptionDeleting(this.id);
}

final class SubscriptionFailure extends SubscriptionState {
  final String message;
  final List<Map<String, dynamic>> items;
  const SubscriptionFailure(this.message, [this.items = const []]);
}