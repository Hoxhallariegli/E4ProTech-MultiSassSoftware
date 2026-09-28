sealed class PlanState {
  const PlanState();
}

final class PlanInitial extends PlanState {
  const PlanInitial();
}

final class PlanLoading extends PlanState {
  final List<Map<String, dynamic>> items;
  const PlanLoading([this.items = const []]);
}

final class PlanLoaded extends PlanState {
  final List<Map<String, dynamic>> items;
  final bool hasMore;
  final bool refreshing;
  const PlanLoaded(this.items, {this.hasMore = false, this.refreshing = false});
}

final class PlanSaving extends PlanState {
  const PlanSaving();
}

final class PlanSaved extends PlanState {
  final Map<String, dynamic> item;
  const PlanSaved(this.item);
}

final class PlanDeleting extends PlanState {
  final int id;
  const PlanDeleting(this.id);
}

final class PlanFailure extends PlanState {
  final String message;
  final List<Map<String, dynamic>> items;
  const PlanFailure(this.message, [this.items = const []]);
}