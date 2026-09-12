sealed class NotificationState {
  const NotificationState();
}

final class NotificationInitial extends NotificationState {
  const NotificationInitial();
}

final class NotificationLoading extends NotificationState {
  const NotificationLoading();
}

final class NotificationLoaded extends NotificationState {
  final List<dynamic> modules;
  final List<dynamic> events;
  const NotificationLoaded({required this.modules, required this.events});
}

final class NotificationError extends NotificationState {
  final String message;
  const NotificationError(this.message);
}
