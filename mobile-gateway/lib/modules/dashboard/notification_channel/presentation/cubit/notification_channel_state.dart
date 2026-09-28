sealed class NotificationChannelState {
  const NotificationChannelState();
}

final class NotificationChannelInitial extends NotificationChannelState {
  const NotificationChannelInitial();
}

final class NotificationChannelLoading extends NotificationChannelState {
  final List<Map<String, dynamic>> items;
  const NotificationChannelLoading([this.items = const []]);
}

final class NotificationChannelLoaded extends NotificationChannelState {
  final List<Map<String, dynamic>> items;
  final bool hasMore;
  final bool refreshing;
  const NotificationChannelLoaded(this.items, {this.hasMore = false, this.refreshing = false});
}

final class NotificationChannelSaving extends NotificationChannelState {
  const NotificationChannelSaving();
}

final class NotificationChannelSaved extends NotificationChannelState {
  final Map<String, dynamic> item;
  const NotificationChannelSaved(this.item);
}

final class NotificationChannelDeleting extends NotificationChannelState {
  final int id;
  const NotificationChannelDeleting(this.id);
}

final class NotificationChannelFailure extends NotificationChannelState {
  final String message;
  final List<Map<String, dynamic>> items;
  const NotificationChannelFailure(this.message, [this.items = const []]);
}