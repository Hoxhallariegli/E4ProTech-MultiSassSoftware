class BookingModel {
  final int? id;
  final int? barberShopId;
  final int? barberId;
  final int? serviceId;
  final int? customerId;
  final String? appointmentAt;
  final String? status;
  final double? totalPrice;
  final String? notes;
  final String? source;

  const BookingModel({
    this.id,
    this.barberShopId,
    this.barberId,
    this.serviceId,
    this.customerId,
    this.appointmentAt,
    this.status,
    this.totalPrice,
    this.notes,
    this.source,
  });

  factory BookingModel.fromJson(Map<String, dynamic> json) => BookingModel(
      id: json['id'] == null ? null : int.tryParse(json['id'].toString()),
      barberShopId: json['barber_shop_id'] == null ? null : int.tryParse(json['barber_shop_id'].toString()),
      barberId: json['barber_id'] == null ? null : int.tryParse(json['barber_id'].toString()),
      serviceId: json['service_id'] == null ? null : int.tryParse(json['service_id'].toString()),
      customerId: json['customer_id'] == null ? null : int.tryParse(json['customer_id'].toString()),
      appointmentAt: json['appointment_at']?.toString(),
      status: json['status']?.toString(),
      totalPrice: json['total_price'] == null ? null : double.tryParse(json['total_price'].toString()),
      notes: json['notes']?.toString(),
      source: json['source']?.toString(),
  );

  Map<String, dynamic> toJson() => {
      'id': id,
      'barber_shop_id': barberShopId,
      'barber_id': barberId,
      'service_id': serviceId,
      'customer_id': customerId,
      'appointment_at': appointmentAt,
      'status': status,
      'total_price': totalPrice,
      'notes': notes,
      'source': source,
  };
}
