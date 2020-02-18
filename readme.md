# Transformation

A package to easily transform data using transformer classes. The idea behind this package is to be able to take data in one format and return it in a different or stripped down format. For example, you might use this when you are making an api endpoint and don't want to return everything or want to return something in multiple formats.

This is the wordpress version of this package. There is also a generic version here: https://github.com/elipettingale/transformation

## How to Install

Just install the package using composer:

    composer require elipettingle/wordpress-transformation
    
## How to Use

The wordpress version of this package includes all the functionality of the generic version, but also works with custom post types and acf fields. Please refer to the readme for the generic package for detailed instructions on how to use this package: https://github.com/elipettingale/transformation.

The following are specific to the wordpress version of the package.

### Post Transformer
 
The Post Transformer can be used in place of the regular transformer class, you can then use this to transform posts or custom post types. It includes compatibility with ACF. A Post Transformer will return a subset of the base wordpress attributes (all attributes are available using $includes) and any acf fields it has.

For example the following transformer:

    class TeamMemberTransformer extends PostTransformer
    {
    
    }
    
Might return the following:

    [
        'ID' => 108,
        'post_title' => 'Brandyn Sporer',
        'post_date' => '2020-02-05 09:27:57'
        'post_status' => 'publish'
        'post_content' => '<!-- wp:paragraph --> <p>Ut beatae et hic eum deserunt sit nihil. Asperiores consequatur quo et quia minus impedit modi voluptas. Consectetur ex voluptatem maiores. Ex ut sint sequi et minus. Molestias dolorem officia consectetur quia. Placeat voluptatem earum error aut dolores doloremque. Sed officiis quod eum saepe at a. Maxime omnis aut deserunt eveniet sit voluptas voluptas.</p> <!-- /wp:paragraph -->'
        'job_title' => 'Airframe Mechanic'
        'department' => 'Logistics'
        'address' => [
            'address' => '353 Little Bourke St, Melbourne VIC 3000, Australia'
            'lat' => -37.8134498
            'lng' => 144.9622691
            'zoom' => 14
            'place_id' => 'ChIJZdO0OLVC1moReE9bPKvo0UQ'
            'street_number' => '353'
            'street_name' => 'Little Bourke Street'
            'street_name_short' => 'Little Bourke St'
            'city' => 'Melbourne;
            'state' => 'Victoria'
            'state_short' => 'VIC'
            'post_code' => '3000'
            'country' => 'Australia'
            'country_short' => 'AU'
        ]
    ]
    
### Jam Packed Example

Here is an example using everything at your disposal:

    class AddressTransformer extends Transformer
    {
        protected $includes = [
            'address',
            'coordinates'
        ];
    
        protected $rename = [
            'address' => 'full_address'
        ];
    
        public function getCoordinatesAttribute()
        {
            return [
                'lat' => $this->item['lat'],
                'lng' => $this->item['lng']
            ];
        }
    }
    
    class TeamMemberTransformer extends PostTransformer
    {
        protected $includes = [
            'post_title',
            'first_name',
            'last_name',
            'initials',
            'address'
        ];
    
        protected $rename = [
            'post_title' => 'full_name'
        ];
    
        public function getFirstNameAttribute()
        {
            return explode(' ', $this->item->post_title)[0];
        }
    
        public function getLastNameAttribute()
        {
            return explode(' ', $this->item->post_title)[1];
        }
    
        public function getInitialsAttribute()
        {
            return $this->getFirstNameAttribute()[0] . $this->getLastNameAttribute()[0];
        }
    
        public function getAddressAttribute()
        {
            return Transform::one($this->item->address, AddressTransformer::class);
        }
    }
    
    $query = new WP_Query([
        'post_type' => 'team_member'
    ]);
    
    $team_members = $query->get_posts();
    $team_members = Transform::all($team_members, TeamMemberTransformer::class);

Using the same data as above this would return:

    [
        'full_name' => 'Brandyn Sporer',
        'first_name' => 'Brandyn',
        'last_name' => 'Sporer',
        'initials' => 'BS',
        'address' => [
            'full_address' => '353 Little Bourke St, Melbourne VIC 3000, Australia',
            'coordinates' => [
                'lat' => -37.8134498,
                'lng' => 144.9622691
            ]
        ]
    ]
