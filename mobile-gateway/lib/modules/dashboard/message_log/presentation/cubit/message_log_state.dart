sealed class MessageLogState {
  const MessageLogState();
}

final class MessageLogInitial extends MessageLogState {
  const MessageLogInitial();
}

final class MessageLogLoading extends MessageLogState {
  final List<Map<String, dynamic>> items;
  const MessageLogLoading([this.items = const []]);
}

final class MessageLogLoaded extends MessageLogState {
  final List<Map<String, dynamic>> items;
  final bool hasMore;
  final bool refreshing;
  const MessageLogLoaded(this.items, {this.hasMore = false, this.refreshing = false});
}

final class MessageLogSaving extends MessageLogState {
  const MessageLogSaving();
}

final class MessageLogSaved extends MessageLogState {
  final Map<String, dynamic> item;
  const MessageLogSaved(this.item);
}

final class MessageLogDeleting extends MessageLogState {
  final int id;
  const MessageLogDeleting(this.id);
}

final class MessageLogFailure extends MessageLogState {
  final String message;
  final List<Map<String, dynamic>> items;
  const MessageLogFailure(this.message, [this.items = const []]);
}