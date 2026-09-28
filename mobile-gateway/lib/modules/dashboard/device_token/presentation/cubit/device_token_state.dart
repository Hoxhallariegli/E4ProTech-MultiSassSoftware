sealed class DeviceTokenState {
  const DeviceTokenState();
}

final class DeviceTokenInitial extends DeviceTokenState {
  const DeviceTokenInitial();
}

final class DeviceTokenLoading extends DeviceTokenState {
  final List<Map<String, dynamic>> items;
  const DeviceTokenLoading([this.items = const []]);
}

final class DeviceTokenLoaded extends DeviceTokenState {
  final List<Map<String, dynamic>> items;
  final bool hasMore;
  final bool refreshing;
  const DeviceTokenLoaded(this.items, {this.hasMore = false, this.refreshing = false});
}

final class DeviceTokenSaving extends DeviceTokenState {
  const DeviceTokenSaving();
}

final class DeviceTokenSaved extends DeviceTokenState {
  final Map<String, dynamic> item;
  const DeviceTokenSaved(this.item);
}

final class DeviceTokenDeleting extends DeviceTokenState {
  final int id;
  const DeviceTokenDeleting(this.id);
}

final class DeviceTokenFailure extends DeviceTokenState {
  final String message;
  final List<Map<String, dynamic>> items;
  const DeviceTokenFailure(this.message, [this.items = const []]);
}