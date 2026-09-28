sealed class ServiceState {
  const ServiceState();
}

final class ServiceInitial extends ServiceState {
  const ServiceInitial();
}

final class ServiceLoading extends ServiceState {
  final List<Map<String, dynamic>> items;
  const ServiceLoading([this.items = const []]);
}

final class ServiceLoaded extends ServiceState {
  final List<Map<String, dynamic>> items;
  final bool hasMore;
  final bool refreshing;
  const ServiceLoaded(this.items, {this.hasMore = false, this.refreshing = false});
}

final class ServiceSaving extends ServiceState {
  const ServiceSaving();
}

final class ServiceSaved extends ServiceState {
  final Map<String, dynamic> item;
  const ServiceSaved(this.item);
}

final class ServiceDeleting extends ServiceState {
  final int id;
  const ServiceDeleting(this.id);
}

final class ServiceFailure extends ServiceState {
  final String message;
  final List<Map<String, dynamic>> items;
  const ServiceFailure(this.message, [this.items = const []]);
}