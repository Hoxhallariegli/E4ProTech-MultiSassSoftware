sealed class BulkCancellationState {
  const BulkCancellationState();
}

final class BulkCancellationInitial extends BulkCancellationState {
  const BulkCancellationInitial();
}

final class BulkCancellationLoading extends BulkCancellationState {
  final List<Map<String, dynamic>> items;
  const BulkCancellationLoading([this.items = const []]);
}

final class BulkCancellationLoaded extends BulkCancellationState {
  final List<Map<String, dynamic>> items;
  final bool hasMore;
  final bool refreshing;
  const BulkCancellationLoaded(this.items, {this.hasMore = false, this.refreshing = false});
}

final class BulkCancellationSaving extends BulkCancellationState {
  const BulkCancellationSaving();
}

final class BulkCancellationSaved extends BulkCancellationState {
  final Map<String, dynamic> item;
  const BulkCancellationSaved(this.item);
}

final class BulkCancellationDeleting extends BulkCancellationState {
  final int id;
  const BulkCancellationDeleting(this.id);
}

final class BulkCancellationFailure extends BulkCancellationState {
  final String message;
  final List<Map<String, dynamic>> items;
  const BulkCancellationFailure(this.message, [this.items = const []]);
}