sealed class WorkingHourState {
  const WorkingHourState();
}

final class WorkingHourInitial extends WorkingHourState {
  const WorkingHourInitial();
}

final class WorkingHourLoading extends WorkingHourState {
  final List<Map<String, dynamic>> items;
  const WorkingHourLoading([this.items = const []]);
}

final class WorkingHourLoaded extends WorkingHourState {
  final List<Map<String, dynamic>> items;
  final bool hasMore;
  final bool refreshing;
  const WorkingHourLoaded(this.items, {this.hasMore = false, this.refreshing = false});
}

final class WorkingHourSaving extends WorkingHourState {
  const WorkingHourSaving();
}

final class WorkingHourSaved extends WorkingHourState {
  final Map<String, dynamic> item;
  const WorkingHourSaved(this.item);
}

final class WorkingHourDeleting extends WorkingHourState {
  final int id;
  const WorkingHourDeleting(this.id);
}

final class WorkingHourFailure extends WorkingHourState {
  final String message;
  final List<Map<String, dynamic>> items;
  const WorkingHourFailure(this.message, [this.items = const []]);
}