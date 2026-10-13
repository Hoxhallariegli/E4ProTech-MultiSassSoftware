sealed class ReportState {
  const ReportState();
}

final class ReportInitial extends ReportState {
  const ReportInitial();
}

final class ReportLoading extends ReportState {
  final List<Map<String, dynamic>> items;
  const ReportLoading([this.items = const []]);
}

final class ReportLoaded extends ReportState {
  final List<Map<String, dynamic>> items;
  final bool hasMore;
  final bool refreshing;
  const ReportLoaded(this.items, {this.hasMore = false, this.refreshing = false});
}

final class ReportSaving extends ReportState {
  const ReportSaving();
}

final class ReportSaved extends ReportState {
  final Map<String, dynamic> item;
  const ReportSaved(this.item);
}

final class ReportDeleting extends ReportState {
  final int id;
  const ReportDeleting(this.id);
}

final class ReportFailure extends ReportState {
  final String message;
  final List<Map<String, dynamic>> items;
  const ReportFailure(this.message, [this.items = const []]);
}