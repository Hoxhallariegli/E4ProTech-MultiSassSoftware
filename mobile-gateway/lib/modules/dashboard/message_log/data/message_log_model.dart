class MessageLogModel {
  final int? id;
  final int? barberShopId;
  final int? customerId;
  final String? channel;
  final String? message;
  final String? status;
  final String? sentAt;

  const MessageLogModel({
    this.id,
    this.barberShopId,
    this.customerId,
    this.channel,
    this.message,
    this.status,
    this.sentAt,
  });

  factory MessageLogModel.fromJson(Map<String, dynamic> json) => MessageLogModel(
      id: json['id'] == null ? null : int.tryParse(json['id'].toString()),
      barberShopId: json['barber_shop_id'] == null ? null : int.tryParse(json['barber_shop_id'].toString()),
      customerId: json['customer_id'] == null ? null : int.tryParse(json['customer_id'].toString()),
      channel: json['channel']?.toString(),
      message: json['message']?.toString(),
      status: json['status']?.toString(),
      sentAt: json['sent_at']?.toString(),
  );

  Map<String, dynamic> toJson() => {
      'id': id,
      'barber_shop_id': barberShopId,
      'customer_id': customerId,
      'channel': channel,
      'message': message,
      'status': status,
      'sent_at': sentAt,
  };
}