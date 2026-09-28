class PlanModel {
  final int? id;
  final String? name;
  final double? price;
  final int? durationMonths;
  final int? maxBarbers;
  final int? maxServices;
  final int? maxShops;
  final bool? active;

  const PlanModel({
    this.id,
    this.name,
    this.price,
    this.durationMonths,
    this.maxBarbers,
    this.maxServices,
    this.maxShops,
    this.active,
  });

  factory PlanModel.fromJson(Map<String, dynamic> json) => PlanModel(
      id: json['id'] == null ? null : int.tryParse(json['id'].toString()),
      name: json['name']?.toString(),
      price: json['price'] == null ? null : double.tryParse(json['price'].toString()),
      durationMonths: json['duration_months'] == null ? null : int.tryParse(json['duration_months'].toString()),
      maxBarbers: json['max_barbers'] == null ? null : int.tryParse(json['max_barbers'].toString()),
      maxServices: json['max_services'] == null ? null : int.tryParse(json['max_services'].toString()),
      maxShops: json['max_shops'] == null ? null : int.tryParse(json['max_shops'].toString()),
      active: json['active'] == true || json['active'] == 1 || json['active'] == '1',
  );

  Map<String, dynamic> toJson() => {
      'id': id,
      'name': name,
      'price': price,
      'duration_months': durationMonths,
      'max_barbers': maxBarbers,
      'max_services': maxServices,
      'max_shops': maxShops,
      'active': active,
  };
}