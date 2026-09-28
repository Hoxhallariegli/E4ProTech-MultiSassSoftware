sealed class BarberShopState {
  const BarberShopState();
}

final class BarberShopInitial extends BarberShopState {
  const BarberShopInitial();
}

final class BarberShopLoading extends BarberShopState {
  final List<Map<String, dynamic>> items;
  const BarberShopLoading([this.items = const []]);
}

final class BarberShopLoaded extends BarberShopState {
  final List<Map<String, dynamic>> items;
  final bool hasMore;
  final bool refreshing;
  const BarberShopLoaded(this.items, {this.hasMore = false, this.refreshing = false});
}

final class BarberShopSaving extends BarberShopState {
  const BarberShopSaving();
}

final class BarberShopSaved extends BarberShopState {
  final Map<String, dynamic> item;
  const BarberShopSaved(this.item);
}

final class BarberShopDeleting extends BarberShopState {
  final int id;
  const BarberShopDeleting(this.id);
}

final class BarberShopFailure extends BarberShopState {
  final String message;
  final List<Map<String, dynamic>> items;
  const BarberShopFailure(this.message, [this.items = const []]);
}