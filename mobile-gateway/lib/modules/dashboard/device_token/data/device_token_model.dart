class DeviceTokenModel {
  final int? id;
  final int? barberShopId;
  final String? userId;
  final String? fcmToken;
  final String? platform;
  final String? lastUsedAt;

  const DeviceTokenModel({
    this.id,
    this.barberShopId,
    this.userId,
    this.fcmToken,
    this.platform,
    this.lastUsedAt,
  });

  factory DeviceTokenModel.fromJson(Map<String, dynamic> json) => DeviceTokenModel(
      id: json['id'] == null ? null : int.tryParse(json['id'].toString()),
      barberShopId: json['barber_shop_id'] == null ? null : int.tryParse(json['barber_shop_id'].toString()),
      userId: json['user_id']?.toString(),
      fcmToken: json['fcm_token']?.toString(),
      platform: json['platform']?.toString(),
      lastUsedAt: json['last_used_at']?.toString(),
  );

  Map<String, dynamic> toJson() => {
      'id': id,
      'barber_shop_id': barberShopId,
      'user_id': userId,
      'fcm_token': fcmToken,
      'platform': platform,
      'last_used_at': lastUsedAt,
  };
}