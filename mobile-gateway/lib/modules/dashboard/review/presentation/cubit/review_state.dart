sealed class ReviewState {
  const ReviewState();
}

final class ReviewInitial extends ReviewState {
  const ReviewInitial();
}

final class ReviewLoading extends ReviewState {
  final List<Map<String, dynamic>> items;
  const ReviewLoading([this.items = const []]);
}

final class ReviewLoaded extends ReviewState {
  final List<Map<String, dynamic>> items;
  final bool hasMore;
  final bool refreshing;
  const ReviewLoaded(this.items, {this.hasMore = false, this.refreshing = false});
}

final class ReviewSaving extends ReviewState {
  const ReviewSaving();
}

final class ReviewSaved extends ReviewState {
  final Map<String, dynamic> item;
  const ReviewSaved(this.item);
}

final class ReviewDeleting extends ReviewState {
  final int id;
  const ReviewDeleting(this.id);
}

final class ReviewFailure extends ReviewState {
  final String message;
  final List<Map<String, dynamic>> items;
  const ReviewFailure(this.message, [this.items = const []]);
}