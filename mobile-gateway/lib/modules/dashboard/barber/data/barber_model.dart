class BarberModel {
  final int? id;
  final int? barberShopId;
  final String? userId;
  final String? name;
  final String? phone;
  final String? photo;
  final String? bio;
  final bool? active;

  const BarberModel({
    this.id,
    this.barberShopId,
    this.userId,
    this.name,
    this.phone,
    this.photo,
    this.bio,
    this.active,
  });

  factory BarberModel.fromJson(Map<String, dynamic> json) => BarberModel(
      id: json['id'] == null ? null : int.tryParse(json['id'].toString()),
      barberShopId: json['barber_shop_id'] == null ? null : int.tryParse(json['barber_shop_id'].toString()),
      userId: json['user_id']?.toString(),
      name: json['name']?.toString(),
      phone: json['phone']?.toString(),
      photo: json['photo']?.toString(),
      bio: json['bio']?.toString(),
      active: json['active'] == true || json['active'] == 1 || json['active'] == '1',
  );

  Map<String, dynamic> toJson() => {
      'id': id,
      'barber_shop_id': barberShopId,
      'user_id': userId,
      'name': name,
      'phone': phone,
      'photo': photo,
      'bio': bio,
      'active': active,
  };
}