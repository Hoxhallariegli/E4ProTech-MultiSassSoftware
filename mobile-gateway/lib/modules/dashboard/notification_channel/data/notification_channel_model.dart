class NotificationChannelModel {
  final int? id;
  final int? barberShopId;
  final String? channel;
  final bool? enabled;
  final int? dailyLimit;

  const NotificationChannelModel({
    this.id,
    this.barberShopId,
    this.channel,
    this.enabled,
    this.dailyLimit,
  });

  factory NotificationChannelModel.fromJson(Map<String, dynamic> json) => NotificationChannelModel(
      id: json['id'] == null ? null : int.tryParse(json['id'].toString()),
      barberShopId: json['barber_shop_id'] == null ? null : int.tryParse(json['barber_shop_id'].toString()),
      channel: json['channel']?.toString(),
      enabled: json['enabled'] == true || json['enabled'] == 1 || json['enabled'] == '1',
      dailyLimit: json['daily_limit'] == null ? null : int.tryParse(json['daily_limit'].toString()),
  );

  Map<String, dynamic> toJson() => {
      'id': id,
      'barber_shop_id': barberShopId,
      'channel': channel,
      'enabled': enabled,
      'daily_limit': dailyLimit,
  };
}