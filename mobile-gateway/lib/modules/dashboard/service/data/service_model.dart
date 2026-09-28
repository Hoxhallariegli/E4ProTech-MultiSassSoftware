class ServiceModel {
  final int? id;
  final int? barberShopId;
  final String? name;
  final String? description;
  final double? price;
  final int? durationMinutes;
  final String? category;
  final bool? active;

  const ServiceModel({
    this.id,
    this.barberShopId,
    this.name,
    this.description,
    this.price,
    this.durationMinutes,
    this.category,
    this.active,
  });

  factory ServiceModel.fromJson(Map<String, dynamic> json) => ServiceModel(
      id: json['id'] == null ? null : int.tryParse(json['id'].toString()),
      barberShopId: json['barber_shop_id'] == null ? null : int.tryParse(json['barber_shop_id'].toString()),
      name: json['name']?.toString(),
      description: json['description']?.toString(),
      price: json['price'] == null ? null : double.tryParse(json['price'].toString()),
      durationMinutes: json['duration_minutes'] == null ? null : int.tryParse(json['duration_minutes'].toString()),
      category: json['category']?.toString(),
      active: json['active'] == true || json['active'] == 1 || json['active'] == '1',
  );

  Map<String, dynamic> toJson() => {
      'id': id,
      'barber_shop_id': barberShopId,
      'name': name,
      'description': description,
      'price': price,
      'duration_minutes': durationMinutes,
      'category': category,
      'active': active,
  };
}