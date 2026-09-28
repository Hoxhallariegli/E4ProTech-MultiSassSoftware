class CustomerModel {
  final int? id;
  final int? barberShopId;
  final String? name;
  final String? phone;
  final String? email;
  final String? photo;
  final int? totalBookings;
  final int? noShowCount;
  final String? blockedAt;

  const CustomerModel({
    this.id,
    this.barberShopId,
    this.name,
    this.phone,
    this.email,
    this.photo,
    this.totalBookings,
    this.noShowCount,
    this.blockedAt,
  });

  factory CustomerModel.fromJson(Map<String, dynamic> json) => CustomerModel(
      id: json['id'] == null ? null : int.tryParse(json['id'].toString()),
      barberShopId: json['barber_shop_id'] == null ? null : int.tryParse(json['barber_shop_id'].toString()),
      name: json['name']?.toString(),
      phone: json['phone']?.toString(),
      email: json['email']?.toString(),
      photo: json['photo']?.toString(),
      totalBookings: json['total_bookings'] == null ? null : int.tryParse(json['total_bookings'].toString()),
      noShowCount: json['no_show_count'] == null ? null : int.tryParse(json['no_show_count'].toString()),
      blockedAt: json['blocked_at']?.toString(),
  );

  Map<String, dynamic> toJson() => {
      'id': id,
      'barber_shop_id': barberShopId,
      'name': name,
      'phone': phone,
      'email': email,
      'photo': photo,
      'total_bookings': totalBookings,
      'no_show_count': noShowCount,
      'blocked_at': blockedAt,
  };
}