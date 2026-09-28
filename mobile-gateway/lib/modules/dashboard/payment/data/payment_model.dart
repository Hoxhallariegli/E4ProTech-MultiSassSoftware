class PaymentModel {
  final int? id;
  final int? barberShopId;
  final int? bookingId;
  final double? amount;
  final String? method;
  final String? status;
  final String? customerName;
  final String? serviceName;
  final String? barberName;

  const PaymentModel({
    this.id,
    this.barberShopId,
    this.bookingId,
    this.amount,
    this.method,
    this.status,
    this.customerName,
    this.serviceName,
    this.barberName,
  });

  factory PaymentModel.fromJson(Map<String, dynamic> json) => PaymentModel(
      id: json['id'] == null ? null : int.tryParse(json['id'].toString()),
      barberShopId: json['barber_shop_id'] == null ? null : int.tryParse(json['barber_shop_id'].toString()),
      bookingId: json['booking_id'] == null ? null : int.tryParse(json['booking_id'].toString()),
      amount: json['amount'] == null ? null : double.tryParse(json['amount'].toString()),
      method: json['method']?.toString(),
      status: json['status']?.toString(),
      customerName: json['customer_name']?.toString() ?? json['booking']?['customer']?['name']?.toString(),
      serviceName: json['service_name']?.toString() ?? json['booking']?['service']?['name']?.toString(),
      barberName: json['barber_name']?.toString() ?? json['booking']?['barber']?['name']?.toString(),
  );

  Map<String, dynamic> toJson() => {
      'id': id,
      'barber_shop_id': barberShopId,
      'booking_id': bookingId,
      'amount': amount,
      'method': method,
      'status': status,
      'customer_name': customerName,
      'service_name': serviceName,
      'barber_name': barberName,
  };
}
