class MessageQueueModel {
  final int? id;
  final int? barberShopId;
  final int? bookingId;
  final String? channel;
  final String? phoneNumber;
  final String? messageContent;
  final String? scheduledAt;
  final String? status;
  final int? retryCount;

  const MessageQueueModel({
    this.id,
    this.barberShopId,
    this.bookingId,
    this.channel,
    this.phoneNumber,
    this.messageContent,
    this.scheduledAt,
    this.status,
    this.retryCount,
  });

  factory MessageQueueModel.fromJson(Map<String, dynamic> json) => MessageQueueModel(
      id: json['id'] == null ? null : int.tryParse(json['id'].toString()),
      barberShopId: json['barber_shop_id'] == null ? null : int.tryParse(json['barber_shop_id'].toString()),
      bookingId: json['booking_id'] == null ? null : int.tryParse(json['booking_id'].toString()),
      channel: json['channel']?.toString(),
      phoneNumber: json['phone_number']?.toString(),
      messageContent: json['message_content']?.toString(),
      scheduledAt: json['scheduled_at']?.toString(),
      status: json['status']?.toString(),
      retryCount: json['retry_count'] == null ? null : int.tryParse(json['retry_count'].toString()),
  );

  Map<String, dynamic> toJson() => {
      'id': id,
      'barber_shop_id': barberShopId,
      'booking_id': bookingId,
      'channel': channel,
      'phone_number': phoneNumber,
      'message_content': messageContent,
      'scheduled_at': scheduledAt,
      'status': status,
      'retry_count': retryCount,
  };
}