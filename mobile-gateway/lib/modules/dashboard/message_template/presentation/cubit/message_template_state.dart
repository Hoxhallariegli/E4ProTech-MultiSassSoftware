sealed class MessageTemplateState {
  const MessageTemplateState();
}

final class MessageTemplateInitial extends MessageTemplateState {
  const MessageTemplateInitial();
}

final class MessageTemplateLoading extends MessageTemplateState {
  final List<Map<String, dynamic>> items;
  const MessageTemplateLoading([this.items = const []]);
}

final class MessageTemplateLoaded extends MessageTemplateState {
  final List<Map<String, dynamic>> items;
  final bool hasMore;
  final bool refreshing;
  const MessageTemplateLoaded(this.items, {this.hasMore = false, this.refreshing = false});
}

final class MessageTemplateSaving extends MessageTemplateState {
  const MessageTemplateSaving();
}

final class MessageTemplateSaved extends MessageTemplateState {
  final Map<String, dynamic> item;
  const MessageTemplateSaved(this.item);
}

final class MessageTemplateDeleting extends MessageTemplateState {
  final int id;
  const MessageTemplateDeleting(this.id);
}

final class MessageTemplateFailure extends MessageTemplateState {
  final String message;
  final List<Map<String, dynamic>> items;
  const MessageTemplateFailure(this.message, [this.items = const []]);
}