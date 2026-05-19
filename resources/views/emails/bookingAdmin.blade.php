<!DOCTYPE html>
<html>
<head>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
    <title>GoodFeet - Broneeringu Kinnitus</title>
</head>
<body style="margin: 0; padding: 0; font-family: Arial, sans-serif; background-color: #f4f4f4;">
    <table role="presentation" style="width: 100%; border-collapse: collapse; background-color: #ffffff; max-width: 600px; margin: auto;">
        <tr>
            <td style="padding: 20px; text-align: center;">
                <img src="{{ asset('assets/img/spot-illustrations/41.png') }}" alt="GoodFeet" width="250" style="max-width: 100%; height: auto;">
                <h3 style="margin-top: 20px; font-weight: bold;">Teie teenusele on tehtud broneering!</h3>
            </td>
        </tr>
        <tr>
            <td style="padding: 20px;">
                <table role="presentation" style="width: 100%; border-collapse: collapse;">
                    <tr>
                        <td style="text-align: left; font-weight: bold;">{{ __('msg.service') }}:</td>
                        <td style="text-align: right;">{{ $service_name }}</td>
                    </tr>
                    <tr>
                        <td style="text-align: left; font-weight: bold;">Klient:</td>
                        <td style="text-align: right;">
                            <p>
                                {{ $client_name }}
                                <br />
                                {{ $client_email }}
                                <br />
                                {{ $client_phone }}
                                <br />
                            </p>
                        </td>
                    </tr>
                    <tr>
                        <td style="text-align: left; font-weight: bold;">{{ __('msg.master') }}:</td>
                        <td style="text-align: right;">{{ $mastername }}</td>
                    </tr>
                    <tr>
                        <td style="text-align: left; font-weight: bold;">{{ __('msg.date') }}:</td>
                        <td style="text-align: right;">{{ $booking_date }}</td>
                    </tr>
                    <tr>
                        <td style="text-align: left; font-weight: bold;">{{ __('msg.time') }}:</td>
                        <td style="text-align: right;">{{ $booking_start }} - {{ $booking_end }}</td>
                    </tr>
                    <tr>
                        <td style="text-align: left; font-weight: bold;">{{ __('msg.address') }}:</td>
                        <td style="text-align: right;">{{ $company_address }}</td>
                    </tr>
                    <tr>
                        <td colspan="2" style="text-align: right;"><strong>{{ __('msg.room_label') }}:</strong> D1053</td>
                    </tr>
                </table>
            </td>
        </tr>
        <tr>
          <td style="padding: 20px;">
              <table role="presentation" style="width: 100%; border-collapse: collapse;">
                  <tr>
                    <td style="padding: 20px; text-align: left; font-weight: bold;">{{ __('msg.service_price') }}:</td>
                    <td style="padding: 20px; text-align: right;">
                        @if ($price_can_change === 1)
                            <span>От </span>
                        @endif
                        {{ $price }} €</td>
                  </tr>
              </table>
          </td>
        </tr>
    </table>
</body>
</html>
