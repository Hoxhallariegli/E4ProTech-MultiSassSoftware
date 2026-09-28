sealed class PaymentState {
  const PaymentState();
}

final class PaymentInitial extends PaymentState {
  const PaymentInitial();
}

final class PaymentLoading extends PaymentState {
  final List<Map<String, dynamic>> items;
  const PaymentLoading([this.items = const []]);
}

final class PaymentLoaded extends PaymentState {
  final List<Map<String, dynamic>> items;
  final bool hasMore;
  final bool refreshing;
  const PaymentLoaded(this.items, {this.hasMore = false, this.refreshing = false});
}

final class PaymentSaving extends PaymentState {
  const PaymentSaving();
}

final class PaymentSaved extends PaymentState {
  final Map<String, dynamic> item;
  const PaymentSaved(this.item);
}

final class PaymentDeleting extends PaymentState {
  final int id;
  const PaymentDeleting(this.id);
}

final class PaymentFailure extends PaymentState {
  final String message;
  final List<Map<String, dynamic>> items;
  const PaymentFailure(this.message, [this.items = const []]);
}