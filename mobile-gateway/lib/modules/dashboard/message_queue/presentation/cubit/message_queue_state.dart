sealed class MessageQueueState {
  const MessageQueueState();
}

final class MessageQueueInitial extends MessageQueueState {
  const MessageQueueInitial();
}

final class MessageQueueLoading extends MessageQueueState {
  final List<Map<String, dynamic>> items;
  const MessageQueueLoading([this.items = const []]);
}

final class MessageQueueLoaded extends MessageQueueState {
  final List<Map<String, dynamic>> items;
  final bool hasMore;
  final bool refreshing;
  const MessageQueueLoaded(this.items, {this.hasMore = false, this.refreshing = false});
}

final class MessageQueueSaving extends MessageQueueState {
  const MessageQueueSaving();
}

final class MessageQueueSaved extends MessageQueueState {
  final Map<String, dynamic> item;
  const MessageQueueSaved(this.item);
}

final class MessageQueueDeleting extends MessageQueueState {
  final int id;
  const MessageQueueDeleting(this.id);
}

final class MessageQueueFailure extends MessageQueueState {
  final String message;
  final List<Map<String, dynamic>> items;
  const MessageQueueFailure(this.message, [this.items = const []]);
}