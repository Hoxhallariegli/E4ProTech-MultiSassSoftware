sealed class CustomerState {
  const CustomerState();
}

final class CustomerInitial extends CustomerState {
  const CustomerInitial();
}

final class CustomerLoading extends CustomerState {
  final List<Map<String, dynamic>> items;
  const CustomerLoading([this.items = const []]);
}

final class CustomerLoaded extends CustomerState {
  final List<Map<String, dynamic>> items;
  final bool hasMore;
  final bool refreshing;
  const CustomerLoaded(this.items, {this.hasMore = false, this.refreshing = false});
}

final class CustomerSaving extends CustomerState {
  const CustomerSaving();
}

final class CustomerSaved extends CustomerState {
  final Map<String, dynamic> item;
  const CustomerSaved(this.item);
}

final class CustomerDeleting extends CustomerState {
  final int id;
  const CustomerDeleting(this.id);
}

final class CustomerFailure extends CustomerState {
  final String message;
  final List<Map<String, dynamic>> items;
  const CustomerFailure(this.message, [this.items = const []]);
}