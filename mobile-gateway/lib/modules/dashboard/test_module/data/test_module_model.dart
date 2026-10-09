class TestModuleModel {
  final int? id;
  final String? name;
  final String? description;
  final double? qty;
  final double? price;
  final bool? isActive;
  final String? dueDate;
  final String? eventAt;
  final String? userId;
  final String? priority;
  final String? image;
  final String? coverPhoto;

  const TestModuleModel({
    this.id,
    this.name,
    this.description,
    this.qty,
    this.price,
    this.isActive,
    this.dueDate,
    this.eventAt,
    this.userId,
    this.priority,
    this.image,
    this.coverPhoto,
  });

  factory TestModuleModel.fromJson(Map<String, dynamic> json) => TestModuleModel(
      id: json['id'] == null ? null : int.tryParse(json['id'].toString()),
      name: json['name']?.toString(),
      description: json['description']?.toString(),
      qty: json['qty'] == null ? null : double.tryParse(json['qty'].toString()),
      price: json['price'] == null ? null : double.tryParse(json['price'].toString()),
      isActive: json['is_active'] == true || json['is_active'] == 1 || json['is_active'] == '1',
      dueDate: json['due_date']?.toString(),
      eventAt: json['event_at']?.toString(),
      userId: json['user_id']?.toString(),
      priority: json['priority']?.toString(),
      image: json['image']?.toString(),
      coverPhoto: json['cover_photo']?.toString(),
  );

  Map<String, dynamic> toJson() => {
      'id': id,
      'name': name,
      'description': description,
      'qty': qty,
      'price': price,
      'is_active': isActive,
      'due_date': dueDate,
      'event_at': eventAt,
      'user_id': userId,
      'priority': priority,
      'image': image,
      'cover_photo': coverPhoto,
  };
}