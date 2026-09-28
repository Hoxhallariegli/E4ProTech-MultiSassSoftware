sealed class BarberState {
  const BarberState();
}

final class BarberInitial extends BarberState {
  const BarberInitial();
}

final class BarberLoading extends BarberState {
  final List<Map<String, dynamic>> items;
  const BarberLoading([this.items = const []]);
}

final class BarberLoaded extends BarberState {
  final List<Map<String, dynamic>> items;
  final bool hasMore;
  final bool refreshing;
  const BarberLoaded(this.items, {this.hasMore = false, this.refreshing = false});
}

final class BarberSaving extends BarberState {
  const BarberSaving();
}

final class BarberSaved extends BarberState {
  final Map<String, dynamic> item;
  const BarberSaved(this.item);
}

final class BarberDeleting extends BarberState {
  final int id;
  const BarberDeleting(this.id);
}

final class BarberFailure extends BarberState {
  final String message;
  final List<Map<String, dynamic>> items;
  const BarberFailure(this.message, [this.items = const []]);
}