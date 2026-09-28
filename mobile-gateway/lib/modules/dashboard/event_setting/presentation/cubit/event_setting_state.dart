sealed class EventSettingState {
  const EventSettingState();
}

final class EventSettingInitial extends EventSettingState {
  const EventSettingInitial();
}

final class EventSettingLoading extends EventSettingState {
  final List<Map<String, dynamic>> items;
  const EventSettingLoading([this.items = const []]);
}

final class EventSettingLoaded extends EventSettingState {
  final List<Map<String, dynamic>> items;
  final bool hasMore;
  final bool refreshing;
  const EventSettingLoaded(this.items, {this.hasMore = false, this.refreshing = false});
}

final class EventSettingSaving extends EventSettingState {
  const EventSettingSaving();
}

final class EventSettingSaved extends EventSettingState {
  final Map<String, dynamic> item;
  const EventSettingSaved(this.item);
}

final class EventSettingDeleting extends EventSettingState {
  final int id;
  const EventSettingDeleting(this.id);
}

final class EventSettingFailure extends EventSettingState {
  final String message;
  final List<Map<String, dynamic>> items;
  const EventSettingFailure(this.message, [this.items = const []]);
}